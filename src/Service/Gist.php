<?php

namespace App\Service;

use App\Repository\GistRepository;
use DateTime;
use InvalidArgumentException;

class Gist
{
    private ?DateTime $since = null;
    private int $perPage;
    private int $page;

    public function __construct(
        private readonly GistRepository $gistRepository,
        private readonly SimpleFile $file
    ) {
    }

    public function setSince(?DateTime $since): static
    {
        $this->since = $since;

        return $this;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Query recent user gists from Gitlab. "Recent" being based on when the user's gists were last queried.
     * @param string $username
     * @return array
     */
    public function getRecentUserGists(string $username): array
    {
        // retrieve any cached since value for the user
        $since = $this->file->read($this->getUserCacheFilePath($username));

        // override the cached since value if it was supplied specifically
        if ($this->since instanceof DateTime) {
            $since = $this->since;
        }

        if ($since !== null) {
            $since = DateTime::createFromFormat('d/m/Y H:i:s', $since);

            if (!($since instanceof DateTime)) {
                throw new InvalidArgumentException('Invalid since date value');
            }
        }

        $this->gistRepository
            ->setPage($this->page)
            ->setPerPage($this->perPage)
            ->setSince($since);

        $gists = $this->gistRepository->fetchAllForUser($username);

        // write the current datetime since value to the cache file based for the user
        $this->file->write(
            $this->getUserCacheFilePath($username),
            (new DateTime())->format('d/m/Y H:i:s')
        );

        return $gists;
    }

    /**
     * Retrieve the path to the user's cache file.
     *
     * @param string $username
     * @return string
     */
    private function getUserCacheFilePath(string $username): string
    {
        return 'data/' . $username . '-gist-since-cache';
    }

    /**
     * Reset the cached "since" query time.
     *
     * @param string $username
     * @return bool
     */
    public function resetUserSinceCache(string $username): bool
    {
        return $this->file->delete('data/' . $username . '-gist-since-cache');
    }
}