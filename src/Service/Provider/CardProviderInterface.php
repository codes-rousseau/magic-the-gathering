<?php
namespace App\Service\Provider;

use App\Dto\SetDto;
use App\Exception\CardProviderException;
use Generator;

Interface CardProviderInterface {

    /**
     * @return Generator<SetDto>
     * @throws CardProviderException
     */
    public function getSets(): Generator;

    /**
     * @param SetDto $set
     * @return Generator<CardDto>
     * @throws CardProviderException
     */
    public function getSetCards(SetDto $set) : Generator;
}