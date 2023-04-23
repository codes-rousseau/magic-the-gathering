<?php
namespace App\Service\Provider;
use App\Dto\CardDto;
use App\Dto\SetDto;
use App\Exception\CardProviderException;
use Exception;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

final class ScryfallProvider implements CardProviderInterface {

    private Client $client;
    private string $scryfallApiEndpoint;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;

    final public function __construct($scryfallApiEndpoint, TranslatorInterface $translator, LoggerInterface $logger) {
        $this->client = new Client();
        $this->scryfallApiEndpoint = $scryfallApiEndpoint;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getSets(): Generator
    {
        try {
            $response = $this->client->request('GET', $this->scryfallApiEndpoint . '/sets', [
                'headers' => ['Accept' => 'application/json']
            ]);
            $decodedResponse = $this->getData($response);
            foreach(($decodedResponse->data??[]) as $item) {
                $setDto = new SetDto();
                $setDto->setId($item->id);
                $setDto->setName($item->name);
                $setDto->setCode($item->code);
                $setDto->setIconUri($item->icon_svg_uri??null);
                $setDto->setReleasedAt($item->released_at??null);
                $setDto->setSearchUri($item->search_uri??null);
                yield $setDto;
            }
        } catch(ConnectException $e) {
            $this->throwCustomException($e, 'exception.provider.connection');
        } catch(Exception $e) {
            $this->throwCustomException($e, 'exception.provider.generic');
        }
    }

    /**
     * @inheritDoc
     */
    public function getSetCards(SetDto $set): Generator
    {
        try {
            yield from $this->getCardsFromUri($set->getSearchUri());
        } catch(ConnectException $e) {
            $this->throwCustomException($e, 'exception.provider.connection');
        } catch(Exception $e) {
            $this->throwCustomException($e, 'exception.provider.generic');
        }
    }

    /**
     * Envoie une exception personnalisée et log des erreurs
     * @param Exception $e
     * @param string $translateCode
     * @throws CardProviderException
     */
    private function throwCustomException(Exception $e, string $translateCode) {
        $this->logger->error($e->getMessage(), ['class' => __CLASS__, 'translateCode' => $translateCode]);
        throw new CardProviderException($this->translator->trans($translateCode, [], 'exceptions'));
    }

    /**
     * Décodage de la réponse
     * @param $response
     * @return mixed
     */
    private function getData($response) {
        return json_decode($response->getBody());
    }

    /**
     * Méthode récursive de récupération des cartes
     * Fait appel à l'API scryfall
     * @param $uri
     * @return Generator
     * @throws GuzzleException
     */
    private function getCardsFromUri($uri): Generator
    {
        $response = $this->client->request('GET', $uri,[
            'headers' => ['Accept' => 'application/json']
        ]);
        $decodedResponse = $this->getData($response);
        if($decodedResponse->has_more) {
            usleep(500000); // 0.5 s afin d'éviter de spammer le service distant
            $this->getCardsFromUri($decodedResponse->next_page);
        }
        foreach($decodedResponse->data as $card) {
            $cardDto = new CardDto();
            $cardDto->setId($card->id);
            $cardDto->setName($card->name);
            $cardDto->setType($card->type_line);
            $cardDto->setDescription($card->oracle_text??"");
            $cardDto->setArtist($card->artist??'');
            $cardDto->setColors($card->colors??[]);
            $cardDto->setImageUris((array)($card->image_uris??null));
            $cardDto->setSetId($card->set_id);
            yield $cardDto;
        }
    }
}