<?php

namespace App\Repository;

use App\Service\PaginationLinkParser;
use DateTime;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GistRepository
{
    private ?DateTime $since = null;
    private int $perPage = 30;
    private int $page = 1;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $apiBaseUrl
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
     * Recursively fetch all public gists for a specified user.
     *
     * @param string $username
     * @param array $responses
     * @return array
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function fetchAllForUser(string $username, array &$responses = []): array
    {
        $response = $this->fetchForUser($username);

        $responses[] = $response->toArray();

        $pagination = [];
        if (isset($response->getHeaders()['link'])) {
            $pagination = PaginationLinkParser::toArray($response->getHeaders()['link'][0]);
        }

        $currPage = $nextPage = $lastPage = $this->page;

        if (isset($pagination['next'])) {
            $nextPage = (int) $pagination['next']['page'];
        }

        if (isset($pagination['last'])) {
            $lastPage = (int) $pagination['last']['page'];
        }

        if ($currPage !== $lastPage) {
            $this->setPage($nextPage);
            $this->fetchAllForUser($username, $responses);
        }

        return array_shift($responses);
    }

    /**
     * Return public gists for a specified user.
     *
     * @param string $username
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function fetchForUser(string $username): ResponseInterface
    {
        $params = [
            'per_page' => $this->perPage,
            'page' => $this->page
        ];

        if ($this->since instanceof DateTime) {
            $params['since'] = $this->since->format('c');
        }

        $queryString = http_build_query($params);
        $url = $this->apiBaseUrl . '/users/' . $username . '/gists?' . $queryString;

        return $this->client->request('GET', $url);
    }
}