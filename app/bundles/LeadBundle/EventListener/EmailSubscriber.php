<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\LeadBundle\Helper\EmailTokenHelper;

/**
 * Class EmailSubscriber
 *
 * @package Mautic\LeadBundle\EventListener
 */
class EmailSubscriber extends CommonSubscriber
{

    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            EmailEvents::EMAIL_ON_BUILD   => array('onEmailBuild', 0),
            EmailEvents::EMAIL_ON_SEND    => array('onEmailGenerate', 0),
            EmailEvents::EMAIL_ON_DISPLAY => array('onEmailDisplay', 0)
        );
    }

    public function onEmailBuild(EmailBuilderEvent $event)
    {
        //add email tokens
        $tokenHelper = new EmailTokenHelper($this->factory);
        $event->addTokenSection('lead.emailtokens', 'mautic.lead.email.header.index', $tokenHelper->getTokenContent(), 255);
    }

    public function onEmailDisplay(EmailSendEvent $event)
    {
        if ($this->factory->getSecurity()->isAnonymous()) {
            $this->onEmailGenerate($event);
        } //else this is a user previewing so leave lead fields tokens in place
    }

    public function onEmailGenerate(EmailSendEvent $event)
    {
        $content  = $event->getContent();
        $regex    = '/{leadfield=(.*?)}/';

        $lead = $event->getLead();

        preg_match_all($regex, $content, $matches);
        if (!empty($matches[1])) {
            $tokenList = array();
            foreach ($matches[1] as $key => $match) {
                $token = $matches[0][$key];

                if (isset($tokenList[$token])) {
                    continue;
                }

                $urlencode = (strpos($match, '|true') !== false);
                $alias     = ($urlencode) ? str_replace('|true', '', $match) : $match;
                $value     = (isset($lead[$alias])) ? $lead[$alias] : '';

                $tokenList[$token] = ($urlencode) ? urlencode($value) : $value;
            }

            $event->addTokens($tokenList);
            unset($tokenList);
        }
    }
}