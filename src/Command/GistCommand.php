<?php

namespace App\Command;

use App\Service\Gist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GistCommand extends Command
{
    const DEFAULT_PAGE_NUM=1;
    const DEFAULT_PER_PAGE=30;

    public function __construct(
        private readonly Gist $gistService
    ) {
        parent::__construct('get-user-gists');
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, "The username of the Github user whose gists you'd like to retrieve")
            ->addOption('page', null, InputOption::VALUE_REQUIRED, 'Which page number of results you want to show')
            ->addOption('perPage', null, InputOption::VALUE_REQUIRED, 'How many gists you want to retrieve at once time (max 30)')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'The date and time in the format of DD/MM/YYYY HH:MM:SS to query gists from')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset the cached since value')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format, either `tabular` (default) or `json`')
        ;
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $page = (int) $input->getOption('page') ?: self::DEFAULT_PAGE_NUM;
        $perPage = (int) $input->getOption('perPage') ?: self::DEFAULT_PER_PAGE;
        $since = $input->getOption('since');
        $format = $input->getOption('format');

        if ($input->getOption('reset')) {
            $this->gistService->resetUserSinceCache($username);
        }

        $this->gistService
            ->setPage($page)
            ->setPerPage($perPage)
            ->setSince($since);

        try {
            $gists = $this->gistService->getRecentUserGists($username);

            switch ($format) {
                case 'table':
                case 'tabular':
                    $tableRows = [];
                    foreach ($gists as $gist) {
                        $tableRows[] = [
                            'id' => $gist['id'],
                            'url' => $gist['url'],
                            'description' => $gist['description'],
                            'owner' => $gist['owner']['login'],
                            'createdAt' => date('d/m/Y H:i:s', strtotime($gist['created_at'])),
                            'updatedAt' => date('d/m/Y H:i:s', strtotime($gist['updated_at'])),
                        ];
                    }

                    $table = new Table($output);
                    $table
                        ->setHeaders(['ID', 'Url', 'Description', 'Owner', 'Created At', 'Updated At'])
                        ->setRows($tableRows);

                    $table->setVertical();
                    $table->render();

                    break;
                case 'json':
                default:
                    print json_encode($gists, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
                    break;
            }

        } catch (\Throwable $ex) {
            $output->writeln('<error>' . $ex->getMessage() . '</error>');
            return Command::FAILURE;

        } catch (\InvalidArgumentException $ex) {
            $output->writeln('<error>' . $ex->getMessage() . '</error>');
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }
}