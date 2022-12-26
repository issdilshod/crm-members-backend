<?php

namespace App\Http\Resources\Contact;

use App\Http\Resources\Helper\AccountSecurityResource;
use App\Http\Resources\Helper\FileResource;
use App\Http\Resources\Helper\RejectReasonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid, 
            'user_uuid' => $this->user_uuid,
            'first_name' => $this->first_name, 
            'last_name' => $this->last_name, 
            'email' => $this->email, 
            'phone_number' => $this->phone_number, 
            'company_name' => $this->company_name, 
            'company_phone_number' => $this->company_phone_number, 
            'company_email' => $this->company_email, 
            'company_website' => $this->company_website, 
            'online_account' => $this->online_account, 
            'account_username' => $this->account_username, 
            'account_password' => $this->account_password, 
            'security_questions' => $this->security_questions, 
            'account_securities' => AccountSecurityResource::collection($this->account_securities), 
            'notes' => $this->notes, 
            'reject_reason' => new RejectReasonResource($this->reject_reason),

            'uploaded_files' => FileResource::collection($this->files),

            'status' => $this->status
        ];
    }
}
