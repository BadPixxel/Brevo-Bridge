<?php

/*
 *  Copyright (C) 2020 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Command;

use BadPixxel\SendinblueBridge\Services\TemplateManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for Compiling and Updating SendInBlue Templates from Sources.
 */
class CompileCommand extends ContainerAwareCommand
{
    /**
     * @var TemplateManager
     */
    private $tmplManager;

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('sendinblue:email:compile')
            ->setDescription("[SendInBlue] Compile Emails Templates")
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TemplateManager $tmplManager */
        $tmplManager = $this->getContainer()->get('badpixxel.sendinblue.templates');
        $this->tmplManager = $tmplManager;
        //==============================================================================
        // Compile All Avalaible Emails
        foreach (array_keys($tmplManager->getAllEmails()) as $emailCode) {
            if (null != $this->compileEmail($emailCode, $output)) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * Compile an Email By Code
     *
     * @param string          $emailCode
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function compileEmail(string $emailCode, OutputInterface $output): int
    {
        //==============================================================================
        // Identify Email Class
        $emailClass = $this->tmplManager->getEmailByCode($emailCode);
        if (is_null($emailClass)) {
            return self::showResult($output, false, $emailCode, 'Unable to identify Email: '.$emailCode);
        }
        //==============================================================================
        // Verify Email Class
        if (!class_exists($emailClass)) {
            return self::showResult($output, false, $emailCode, 'Email Class: '.$emailClass.' was not found');
        }
        //==============================================================================
        // Check if Email Needs to Be Compiled
        if (!$this->tmplManager->isTemplateAware($emailClass)) {
            return self::showResult($output, true, $emailCode, 'Email Class: '.$emailCode.' do not manage Templates');
        }
        //==============================================================================
        // Compile Email Template Raw Html
        $rawHtml = $this->tmplManager->compile($emailClass);
        if (is_null($rawHtml)) {
            return self::showResult(
                $output,
                false,
                $emailCode,
                'Exception => '.$this->tmplManager->getLastError()
            );
        }
        //==============================================================================
        // Update Email Template On Host
        if (null == $this->tmplManager->update($emailClass, $rawHtml)) {
            return self::showResult(
                $output,
                false,
                $emailCode,
                'Exception => '.$this->tmplManager->getLastError()
            );
        }

        return self::showResult($output, true, $emailCode, 'Successfully Compiled');
    }

    /**
     * Render result in Console
     *
     * @param OutputInterface $output
     * @param bool            $result
     * @param string          $code
     * @param string          $text
     *
     * @return int
     */
    protected static function showResult(OutputInterface $output, bool $result, string $code, string $text): int
    {
        $status = $result ? '[<info> OK </info>]' : '[<error> KO </error>]';
        $output->writeln($status.' '.ucfirst($code).' : '.$text);

        return $result ? 0 : -1;
    }
}
