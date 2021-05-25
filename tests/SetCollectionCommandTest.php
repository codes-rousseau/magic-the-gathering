<?php

namespace tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SetCollectionCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('SetCollectionCommand');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['Commander 2021 Tokens']);

        $commandTester->execute([
            'command' => $command->getName()
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The cards of the collection have been saved in the database.', $output);

    }


}