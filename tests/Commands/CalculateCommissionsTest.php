<?php

declare(strict_types=1);

namespace App\Tests\Commands;

use App\Commands\CalculateCommissions;
use App\DTOs\Transaction;
use App\Services\CommissionCalculator\CommissionCalculator;
use App\Services\TransactionsParser\TransactionsParser;
use App\Services\TransactionsParser\TransactionsParserInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function PHPUnit\Framework\once;

class CalculateCommissionsTest extends TestCase
{
    public function testUserCanRunCalculation(): void
    {
        $transactionsParser = $this->createMock(TransactionsParserInterface::class);
        $transactionsParser->expects(self::once())->method('parseFile')->willReturn([
            new Transaction(123, 1, 'USD'),
            new Transaction(122, 1, 'USD'),
            new Transaction(124, 1, 'USD'),
        ]);

        $commissionCalculator = $this->createMock(CommissionCalculator::class);
        $commissionCalculator->expects(self::exactly(3))->method('calculate')->willReturn(1.00);

        $command = new CalculateCommissions($transactionsParser, $commissionCalculator);

        $input = $this->createMock(InputInterface::class);
        $input->expects(once())->method('getArgument')->willReturn('file.txt');

        $output = $this->createMock(OutputInterface::class);
        $output->expects(self::exactly(3))->method('write');

        self::assertEquals(Command::SUCCESS, $command->run($input, $output));
    }

    public function testCommandReturnFailureCodeOnException(): void
    {
        $transactionsParser = new TransactionsParser();
        $commissionCalculator = $this->createMock(CommissionCalculator::class);

        $command = new CalculateCommissions($transactionsParser, $commissionCalculator);

        $input = $this->createMock(InputInterface::class);
        $input->expects(once())->method('getArgument')->willReturn('file.txt');

        $output = $this->createMock(OutputInterface::class);
        $output->expects(self::exactly(2))->method('write');

        self::assertEquals(Command::FAILURE, $command->run($input, $output));
    }
}