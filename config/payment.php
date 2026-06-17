<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Card fields to exclude from session flash (PCI-DSS)
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the card-data field names that must never be
    | flashed into the session. Consumed by two distinct leak vectors:
    |
    |   - UsersController::cardSafeInput() — strips these from manual
    |     ->withInput() bouncebacks (Vector B).
    |   - bootstrap/app.php $exceptions->dontFlash() — strips these from the
    |     framework's automatic flash on a ValidationException (Vector A).
    |
    | Includes both the posted hidden names (card_*) and the visible modal_*
    | inputs, since both are submitted with the intake form.
    |
    */

    'card_fields_to_exclude' => [
        'card_number',    'modal_card_number',
        'card_cvc',       'modal_cvc',
        'card_exp_month', 'modal_exp_month',
        'card_exp_year',  'modal_exp_year',
    ],

];
