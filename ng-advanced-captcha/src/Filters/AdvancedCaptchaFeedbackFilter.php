<?php

namespace Plugins\AdvancedCaptcha\Filters;

use Plugins\AdvancedCaptcha\AdvancedCaptcha;
use function Plugins\{notify, logger};

class AdvancedCaptchaFeedbackFilter
{
    /**
     * @var AdvancedCaptcha
     */
    protected $captcha;

    public function __construct(AdvancedCaptcha $captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * Called when showing feedback form
     */
    public function onShow($form_id, $frow, $fData, &$tVars)
    {
        $tVars['captcha_widget'] = $this->captcha->generateWidget('feedback');
        logger('ng-advanced-captcha: Feedback widget added to form', 'info');
    }

    /**
     * Called when processing feedback submission (old style)
     */
    public function onProcess($form_id, $frow, $fData, $flagHTML, &$tVars)
    {
        // Legacy support - do nothing here, use onProcessEx instead
    }

    /**
     * Called when processing feedback submission (new style)
     * Returns false to block submission
     */
    public function onProcessEx($form_id, $frow, $fData, $flagHTML, &$tVars, &$tResult)
    {
        if (! $this->captcha->verifying('feedback')) {
            $error = $this->captcha->rejectionReason();
            logger('ng-advanced-captcha: Feedback blocked - ' . $error, 'warning');
            $tResult = [
                'status' => 0,
                'msg' => $error,
                'rawmsg' => $error,
                'field' => '',
            ];
            return false;
        }

        logger('ng-advanced-captcha: Feedback captcha verified', 'info');
        return true;
    }

    /**
     * Called when sending feedback email
     * Return true to indicate email was sent by plugin
     */
    public function onSendEx($form_id, $frow, $fData, $eNotify, &$tVars, &$tResult)
    {
        // We don't handle email sending, return false
        return false;
    }

    /**
     * Called after feedback processing is complete
     */
    public function onProcessNotify($form_id)
    {
        // No post-processing needed for captcha
    }
}
