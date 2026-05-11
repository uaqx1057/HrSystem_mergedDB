<?php

namespace App\Notifications;

use App\Models\AdvanceSalary;
use App\Models\EmailNotificationSetting;
use Illuminate\Notifications\Messages\MailMessage;

class AdvanceSalaryStatusUpdate extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $advanceSalary;
    private $emailSetting;

    public function __construct(AdvanceSalary $advanceSalary)
    {
        // Don't use queue - send immediately
        $this->onQueue(null);
        $this->afterCommit();

        $this->advanceSalary = $advanceSalary;
        $this->company = $this->advanceSalary->employee->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'advance-salary-status-update')->first();

        if (!$this->emailSetting) {
            $this->emailSetting = EmailNotificationSetting::create([
                'company_id' => $this->company->id,
                'setting_name' => 'Advance Salary Status Update',
                'slug' => 'advance-salary-status-update',
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'send_twilio' => 'no',
            ]);
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting && $this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build();
        $url = route('advance-salaries.index');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('Advance Salary Status Update') . '<br>' . __('app.date') . ': ' . $this->advanceSalary->date->format($this->company->date_format) . '<br>' . __('app.amount') . ': ' . $this->advanceSalary->advance_salary . '<br>' . __('app.status') . ': ' . $this->advanceSalary->status;

        if ($this->advanceSalary->status == 'approved' && !is_null($this->advanceSalary->approve_reason)) {
            $content .= '<br>' . __('messages.reasonForApproval') . ': ' . $this->advanceSalary->approve_reason;
        } elseif ($this->advanceSalary->status == 'rejected' && !is_null($this->advanceSalary->reject_reason)) {
            $content .= '<br>' . __('messages.reasonForRejection') . ': ' . $this->advanceSalary->reject_reason;
        }

        return $build
            ->subject(__('Advance Salary Status Update') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('View Details'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
//phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->advanceSalary->toArray();
    }

}