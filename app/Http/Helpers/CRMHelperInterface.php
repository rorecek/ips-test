<?php

namespace App\Http\Helpers;

interface CRMHelperInterface
{
    public function getContact($email);
    public function addTag($contact_id, $tag_id);
}