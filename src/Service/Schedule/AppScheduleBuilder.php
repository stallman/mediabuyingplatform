<?php
namespace App\Service\Schedule;

use Zenstruck\ScheduleBundle\Schedule;
use Zenstruck\ScheduleBundle\Schedule\ScheduleBuilder;

class AppScheduleBuilder implements ScheduleBuilder
{

    public function buildSchedule(Schedule $schedule): void
    {
        //$this->addLetsEncrypt($schedule);
        $this->calculateDesignStatistic($schedule);
        $this->calculateAlgorithmStatistic($schedule);
        $this->calculatePercentApprove($schedule);
        $this->calculateNewsECPM($schedule);
        $this->calculateTeasersECPM($schedule);
        $this->updateExchangeCurrency($schedule);
        $this->generateOtherFiltersData($schedule);
        $this->generateTeaserStatistic($schedule);
        $this->generateNewsStatistic($schedule);
        $this->generateNewsBuyerStatistic($schedule);
        $this->checkCost($schedule);
        $this->cleanStats($schedule);
    }

    /**
     * Выполнение команды автоматического получения letsencrypt сертификата
     * каждую минуту в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function addLetsEncrypt(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:auto-letsencrypt:domain')
            ->everyMinute();
    }

    /**
     * Выполнение команды расчета агрегированной статистики по дизайнам для каждого баера
     * каждый час в 5 минут, в окружениях PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function calculateDesignStatistic(Schedule $schedule)
    {
        $schedule
            ->environments('prod','team', 'client')
            ->addCommand('app:design-stat:calculate')
            ->cron('5 */1 * * *');
    }

    /**
     * Выполнение команды расчета агрегированной статистики по алгоритмам для каждого баера
     * каждый час в 10 минут, в окружениях PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function calculateAlgorithmStatistic(Schedule $schedule)
    {
        $schedule
            ->environments('prod','team', 'client')
            ->addCommand('app:algorithm-stat:calculate')
            ->cron('10 */1 * * *');
    }

    /**
     * Выполнение команды автоматического расчета % апрува для подгрупп
     * каждый час в 15 минут, в окружении PROD, CLIENT, TEAM
     * @param Schedule $schedule
     */
    private function calculatePercentApprove(Schedule $schedule)
    {
        $schedule
            ->environments('prod','team', 'client')
            ->addCommand('app:percent-approve:calculate')
            ->cron('15 */1 * * *');
    }

    /**
     * Выполнение команды рассчета eCPM для новостей
     * каждый час в 20 минут, в окружении PROD, CLIENT, TEAM
     * @param Schedule $schedule
     */
    private function calculateNewsECPM(Schedule $schedule)
    {
        $schedule
            ->timezone('Europe/Moscow')
            ->environments('prod',  'team', 'client')
            ->addCommand('app:news-ecpm:calculate')
            ->cron('20 */1 * * *');
    }

    /**
     * Выполнение команды рассчета eCPM для тизеров
     * каждый час в 25 минут, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function calculateTeasersECPM(Schedule $schedule)
    {
        $schedule
            ->timezone('Europe/Moscow')
            ->environments('prod', 'team', 'client')
            ->addCommand('app:teasers-ecpm:calculate')
            ->cron('25 */1 * * *');
    }

    /**
     * Выполнение команды обновления обменного курса валют.
     * каждый час в 30 минут, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function updateExchangeCurrency(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:exchange-currency-rate:update')
            ->cron('30 */1 * * *');
    }

    /**
     * Выполнение команды для генерации данных для раздела "доп фильтры" в модуле "Анализ трафика" из таблицыVisits,
     * каждый час в 35 минут, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function generateOtherFiltersData(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:other-filters-data:generate')
            ->cron('35 */1 * * *');
    }

    /**
     * Выполнение команды генерации статистики для тизеров
     * каждый час в 40 минут, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function generateTeaserStatistic(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:teaser:statistics')
            ->cron('40 */1 * * *');
    }

    /**
     * Выполнение команды генерации статистики для новостей
     * каждый час в 45 минут, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function generateNewsStatistic(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:news-stat:calculate')
            ->cron('45 */1 * * *');
    }

    /**
     * Выполнение команды генерации статистики для новостей
     * каждый час в 50 минут, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function generateNewsBuyerStatistic(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:news-stat-buyer:calculate')
            ->cron('50 */1 * * *');
    }

    /**
     * Выполнение команды проверки расходов
     * каждую минуту, в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function checkCost(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:check:cost')
            ->everyMinute();
    }

    /**
     * Очистка данных статистики из БД
     * каждый день ночью в окружении PROD, TEAM, CLIENT
     * @param Schedule $schedule
     */
    private function cleanStats(Schedule $schedule)
    {
        $schedule
            ->environments('prod', 'team', 'client')
            ->addCommand('app:clean-stats')
            ->arguments('-d 0')     //disable DRY RUN
            ->dailyBetween(2, 5);
    }
}
