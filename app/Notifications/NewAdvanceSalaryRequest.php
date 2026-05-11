<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\AdvanceSalary;
use Illuminate\Notifications\Messages\MailMessage;

class NewAdvanceSalaryRequest extends BaseNotification
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
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-advance-salary-request')->first();

        if (!$this->emailSetting) {
            $this->emailSetting = EmailNotificationSetting::create([
                'company_id' => $this->company->id,
                'setting_name' => 'New Advance Salary Request',
                'slug' => 'new-advance-salary-request',
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

        $content = __('Advance Salary Request') . ':- ' . '<br>' . __('app.employee') . ': ' . $this->advanceSalary->employee->name . '<br>' . __('app.date') . ': ' . $this->advanceSalary->date->toDayDateTimeString() . '<br>' . __('app.amount') . ': ' . $this->advanceSalary->advance_salary . '<br>' . __('app.status') . ': ' . $this->advanceSalary->status;

        return $build
            ->subject(__('Advance Salary Request') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('View Request'),
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