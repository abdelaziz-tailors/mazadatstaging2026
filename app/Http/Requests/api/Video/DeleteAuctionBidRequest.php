<?php

namespace App\Http\Requests\api\Video;

class DeleteAuctionBidRequest extends AddVideoCommentRequest
{
    public function validationData()
    {
        // Body/query parameters must never override the URL identifier.
        return ['bid_id' => $this->route('bid_id')];
    }

    public function rules()
    {
        return ['bid_id' => 'required|integer|min:1|max:9223372036854775807'];
    }
}
