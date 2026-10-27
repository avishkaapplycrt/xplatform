<?php

namespace App\Services\MockMaster;

use App\Services\WhatsAppCloudService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one email or WhatsApp message to one Mock Master student. Contact
 * details are always looked up here from mm_studentuser by student id, so
 * the browser never supplies (or sees) the address it sends to. Used by the
 * single composer and by "Send to all" on the Campaign tab.
 */
class MockMasterMessenger
{
    /**
     * @return array{ok: bool, status: string, to: ?string, name: ?string, message: string, error: ?string}
     *         status is "sent", "failed" or "skipped" (no address on file); error is the raw failure detail.
     */
    public function sendEmail(int $studentId, string $subject, string $body, string $type = 'single', int $bulkCount = 1): array
    {
        $student = DB::table('mm_studentuser')
            ->where('studentId', $studentId)
            ->first(['email', 'first_name', 'last_name']);
        $email = $student->email ?? null;
        $name = $student ? trim($student->first_name . ' ' . $student->last_name) : null;

        $log = function (string $status, ?string $error = null) use ($email, $name, $subject, $body, $type, $bulkCount) {
            DB::table('email_logs')->insert([
                'client_id' => Auth::guard('client')->id(),
                'email_address' => (string) $email,
                'recipient_name' => $name,
                'subject' => mb_substr($subject, 0, 255),
                'body' => $body,
                'type' => $type,
                'bulk_count' => $bulkCount,
                'status' => $status,
                'error_message' => $error,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $log('failed', 'No valid email address on file.');
            return $this->result(false, 'skipped', $email, $name, 'This student has no valid email address on file.', 'No valid email address on file.');
        }

        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Throwable $e) {
            report($e);
            $error = mb_substr($e->getMessage(), 0, 1000);
            $log('failed', $error);
            return $this->result(false, 'failed', $email, $name, 'The email could not be sent. Please try again.', $error);
        }

        $log('sent');

        return $this->result(true, 'sent', $email, $name, 'Email sent to ' . $email, null);
    }

    /** @return array{ok: bool, status: string, to: ?string, name: ?string, message: string, error: ?string} */
    public function sendWhatsApp(int $studentId, string $body): array
    {
        $student = DB::table('mm_studentuser')
            ->where('studentId', $studentId)
            ->first(['country_code', 'phone', 'first_name', 'last_name']);
        $name = $student ? trim($student->first_name . ' ' . $student->last_name) : null;
        $digits = $student ? preg_replace('/\D/', '', ($student->country_code ?? '') . ($student->phone ?? '')) : '';

        if ($digits === '') {
            return $this->result(false, 'skipped', null, $name, 'This student has no phone number on file.', 'No phone number on file.');
        }

        $result = (new WhatsAppCloudService())->sendText('+' . $digits, $body);

        if (!$result['ok']) {
            $outsideWindow = str_contains((string) $result['error'], '131047');
            return $this->result(false, 'failed', '+' . $digits, $name, $outsideWindow
                ? 'WhatsApp only allows free text within 24 hours of the student\'s last message. Use an approved template for this student.'
                : 'WhatsApp could not send the message: ' . $result['error'], (string) $result['error']);
        }

        return $this->result(true, 'sent', '+' . $digits, $name, 'WhatsApp sent to +' . $digits, null);
    }

    private function result(bool $ok, string $status, ?string $to, ?string $name, string $message, ?string $error): array
    {
        return compact('ok', 'status', 'to', 'name', 'message', 'error');
    }
}
