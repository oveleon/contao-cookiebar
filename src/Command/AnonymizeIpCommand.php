<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @author      Sebastian Zoglowek    <https://github.com/zoglo>
 * @copyright   Oveleon               <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Oveleon\ContaoCookiebar\Model\CookieLogModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Synchronizes the file system with the database.
 *
 * @internal
 */
#[AsCommand(
    name: 'cookiebar:anonymizeip',
    description: 'Anonymizes all IP addresses in the log',
)]
class AnonymizeIpCommand extends Command
{
    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->framework->initialize();
        $objLog = CookieLogModel::findAll();

        if (null !== $objLog)
        {
            $io->progressStart($objLog->count());

            while ($objLog->next())
            {
                $objLog->ip = IPUtils::anonymize($objLog->ip);
                $objLog->save();

                $io->progressAdvance();
            }

            $io->progressFinish();
            $io->success(sprintf('%s ip addresses were successfully anonymized.', $objLog->count()));
        }

        return Command::SUCCESS;
    }
}
