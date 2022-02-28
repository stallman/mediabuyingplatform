<?php


namespace App\Traits\Dashboard;

trait StatisticConstTrait
{
    public function getPeriods()
    {
        return [
            'today' => 'Сегодня',
            'yesterday' => 'Вчера',
            'day-before-yesterday' => 'Позавчера',
            'current-week' => 'Текущая неделя',
            'last-week' => 'Прошлая неделя',
            'current-month' => 'Текущий месяц',
            'last-month' => 'Прошлый месяц',
            'week' => '-7 дней',
            'month' => '-30 дней',
            'two-month' => '-60 дней',
            'three-month' => '-90 дней',
        ];
    }

    public function getTraffic()
    {
        return [
            'visits' => 'Клики',
            'visits_percent' => 'Клики (%)',
            'uniq_visits' => 'Уники',
            'uniq_visits_percent' => 'Уники (%)',
            'click_count' => 'КПТ',
            'percent_of_total_click_count' => 'КПТ (%)',
            'percent_probiv' => 'Пробив (%)',
        ];
    }

    public function getLeads()
    {
        return [
            'total_leads' => 'Лиды',
            'leads_approve_count' => 'Подтв.',
            'percent_leads_approve' => 'Подтв. (%)',
            'leads_pending_count' => 'Холд',
            'percent_leads_pending' => 'Холд (%)',
            'leads_declined_count' => 'Отклон.',
            'percent_leads_declined' => 'Отклон. (%)',
        ];
    }

    public function getFinances()
    {
        return [
            'cr_conversion' => 'CR',
            'middle_lead' => 'Сред. лид',
            'real_income' => 'Доход',
            'real_epc' => 'EPC',
            'lead_price' => 'Цена лида',
            'real_roi' => 'ROI',
            'epc_projected' => 'EPC прогн.',
            'consumption' => 'Расход',
            'income_projected' => 'Доход прогн.',
            'roi_projected' => 'ROI прогн.',
        ];
    }

    public function translateTrafficType()
    {
        return [
            'desktop' => 'Компьютер',
            'tablet' => 'Планшет',
            'mobile' => 'Мобайл',
        ];
    }

    public function translateDaysOfWeek()
    {
        return [
            'monday' => 'Понедельник',
            'tuesday' => 'Вторник',
            'wednesday' => 'Среда',
            'thursday' => 'Четверг',
            'friday' => 'Пятница',
            'saturday' => 'Суббота',
            'sunday' => 'Воскресенье',
        ];
    }

    public function getSettingsFields()
    {
        return [
            'traffic' => $this->getTraffic(),
            'leads' => $this->getLeads(),
            'finances' => $this->getFinances()
        ];
    }
}