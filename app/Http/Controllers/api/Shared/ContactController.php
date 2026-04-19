<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Shared\ContactMessageRequest;
use App\Models\ContactMessage;
use App\Traits\ResponseTrait;

class ContactController extends Controller
{
    use ResponseTrait;

    public function store(ContactMessageRequest $request)
    {
        ContactMessage::create($request->validated());

        return $this->success_response(
            TranslationHelper::translate('your_message_was_sent_successfully'),
            []
        );
    }
}
