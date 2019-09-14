<?php

namespace App\Http\Controllers;

use App\Actions\CalculateReminderTagIdAction;
use App\Http\Helpers\CRMHelperInterface;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * @var CRMHelperInterface
     */
    private $crm;

    /**
     * @var CalculateReminderTagIdAction
     */
    private $calculateReminderTag;

    public function __construct(
        CRMHelperInterface $crm,
        CalculateReminderTagIdAction $calculateReminderTag
    ) {
        $this->crm = $crm;
        $this->calculateReminderTag = $calculateReminderTag;
    }

    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules());
        $email = $request->get('contact_email');

        try {
            $contact = $this->crm->getContact($email);
            $tagId = $this->calculateReminderTag->execute($email, $contact['_Products']);
            $success = $this->crm->addTag($contact['Id'], $tagId);
            $message = $success ? "Tag $tagId added successfully" : 'No tag has been added';
        } catch (\RuntimeException $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    protected function rules()
    {
        return [
            'contact_email' => [
                'required',
                'exists:users,email',
            ],
        ];
    }
}
