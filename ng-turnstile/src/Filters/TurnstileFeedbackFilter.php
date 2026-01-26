<?php

namespace Plugins\Turnstile\Filters;

use Plugins\Turnstile\Turnstile;
use function Plugins\{notify, logger};

class TurnstileFeedbackFilter
{
    /**
     * @var Turnstile
     */
    protected $turnstile;

    public function __construct(Turnstile $turnstile)
    {
        $this->turnstile = $turnstile;
    }

    public function sendFeedback($params)
    {
        if (! $this->turnstile->verifying()) {
            $error = $this->turnstile->rejectionReason();
            notify('error', $error);
            logger('ng-turnstile: Feedback blocked - ' . $error, 'warning');
            return [
                'status' => 0,
                'errorText' => $error,
            ];
        }

        logger('ng-turnstile: Feedback captcha verified', 'info');
        return true;
    }
}
