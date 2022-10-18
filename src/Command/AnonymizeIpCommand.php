<?php

declare(strict_types=1);

/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Oveleon\ContaoCookiebar\Model\CookieLogModel;
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
class AnonymizeIpCommand extends Command
{
    protected static $defaultName = 'cookiebar:anonymizeip';

    protected $framework;

    public function __construct(ContaoFramework $contaoFramework)
    {
        $this->framework = $contaoFramework;
        $this->framework->initialize();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Anonymizes all IP addresses in the log');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $objLog = CookieLogModel::findAll();

        if(null !== $objLog)
        {
            $io->progressStart($objLog->count());

            while($objLog->next())
            {
                $objLog->ip = IPUtils::anonymize($objLog->ip);
                $objLog->save();

                $io->progressAdvance();
            }

            $io->progressFinish();
            $io->success(sprintf('%s ip addresses were successfully anonymized.', $objLog->count()));
        }

        return 0;
    }
}
