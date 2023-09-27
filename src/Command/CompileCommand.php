<?php

/*
 *  Copyright (C) BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\BrevoBridge\Command;

use BadPixxel\BrevoBridge\Services\TemplateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for Compiling and Updating SendInBlue Templates from Sources.
 */
class CompileCommand extends Command
{
    /**
     * @var TemplateManager
     */
    private $tmplManager;

    /**
     * Command Constructor
     *
     * @param TemplateManager $tmplManager
     * @param null|string     $name
     */
    public function __construct(TemplateManager $tmplManager, string $name = null)
    {
        $this->tmplManager = $tmplManager;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:email:compile')
            ->setDescription("[Brevo] Compile Emails Templates")
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //==============================================================================
        // Compile All Available Emails
        foreach (array_keys($this->tmplManager->getAllEmails()) as $emailCode) {
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
