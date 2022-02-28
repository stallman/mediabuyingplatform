<?php

namespace App\Service;


class CalculateStatistic
{

    /**
     * Метод расчета CTR (click-through rate). Соотношение кликов к показам ((клики / показы) * 100%)
     *
     * @param int $clickCount - количество кликов
     * @param int $showCount - количество показов
     * @return float|int
     */
    public function calculateCTR(int $clickCount, int $showCount)
    {
        if ($showCount != 0 ) {
            return $clickCount / $showCount * 100;
        }

        return 0;
    }

    /**
     * Метод расчета eCPM (effective cost per mille). Соотношении суммы доходов к показам * 1000
     * (рубли / показы * 1000)
     *
     * @param float|int $amountSum - сумма дохода
     * @param int $showCount - количество показов
     * @return float|int
     */
    public function calculateECPM($amountSum, int $showCount)
    {
        if ($amountSum && $showCount != 0) {
            return $amountSum / $showCount * 1000;
        }

        return 0;
    }

    /**
     * Метод расчета пробива. Соотношении кликов по тизерам к кликам * 100%
     * (клики на тизерах / клики * 100%)
     *
     * @param int $teasersClickCount - количество кликов по тизерам
     * @param int $clickCount - количество кликов
     * @return float|int
     */
    public function calculateProbiv(int $teasersClickCount, int $clickCount)
    {
        if ($clickCount != 0) {
            return $teasersClickCount / $clickCount * 100;
        }

        return 0;
    }

    /**
     * Уники - уникальные визиты
     * (клики по тизерам / уники) * 100%.
     */
    public function calculateUniqVisitsProbiv(int $teasersClickCount, int $uniqVisitsCount)
    {
        if ($uniqVisitsCount != 0) {
            return ($teasersClickCount / $uniqVisitsCount) * 100;
        }

        return 0;
    }

    /**
     * Метод расчета аппрува. Процент подтвержденных лидов от общего их количества (подтвержденные лиды / все лиды * 100).
     *
     * @param int $approvedConversionsCount - количество подтвержденных конверсий
     * @param int $allConversionsCount - обищее количество конверсий
     * @return float|int
     */
    public function calculateApprove(int $approvedConversionsCount, int $allConversionsCount)
    {
        if ($allConversionsCount != 0) {
            return $approvedConversionsCount / $allConversionsCount * 100;
        }

        return 0;
    }

    /**
     * Метод расчета EPC (earn per click). Соотношение суммы доходов к кликам (рубли / клики)
     *
     * @param float|int $amountSum - сумма дохода
     * @param int $clickCount - количество кликов
     * @return float|int
     */
    public function calculateEPC($amountSum, int $clickCount)
    {
        if ($clickCount) {
            return $amountSum / $clickCount;
        }

        return 0;
    }

    /**
     * Метод расчета CR, conversion rate, конверт. Соотношение полученных лидов к кликам ((лиды / клики) * 100%)
     * Максмальное значение 9999.9999
     *
     * @param int $conversionsCount - количество конверсий
     * @param int $clickCount - количество кликов
     * @return float|int
     */
    public function calculateCR(int $conversionsCount, int $clickCount, int $precision = 2)
    {
        $max = 9999.9999;

        if ($clickCount) {
            $result = round(($conversionsCount / $clickCount) * 100, $precision);

            if ($result > $max) {
                $result = $max;
            }

            return $result;
        }

        return 0;
    }

    /**
     * Метод расчета стоимости лида. Соотношение суммы доходов/расходов к количеству лидов (сумма / лиды)
     *
     * @param float|int $amountSum - сумма дохода/расхода
     * @param int $leadCount - кличество лидов
     * @return float|int
     */
    public function calculateLeadPrice($amountSum, int $leadCount)
    {
        if ($leadCount) {
            // Сумма выплат за подтверждённые лиды / их колличество
            return $amountSum / $leadCount;
        }

        return 0;
    }

    /**
     * Метод расчета ROI (return on investment). Возврат инвестиций
     * рассчитывается по формуле: (100 * (реальный доход - расход) / расход)
     * Результат показывать со знаком %
     * если значение расход равно нулю. То установить значение 0%
     *
     * @param float|int $amountIncome - сумма дохода
     * @param float|int $amountCost - сумма расхода
     * @return float|int
     */
    public function calculateROI($amountIncome, $amountCost)
    {
        if ($amountCost) {
            return 100 * ($amountIncome - $amountCost) / $amountCost;
        }

        return 0;
    }

    /**
     * Метод расчета Прогнозируемый ROI.
     * рассчитывается по формуле: 100*(Прогнозируемый доход-Расход)/Расход
     *
     * @param float|int $amountIncome - сумма дохода
     * @param float|int $amountCost - сумма расхода
     * @param float|int $consumption - расход
     * @return float|int
     */
    public function calculateROIProjected($amountIncome, $amountCost, $consumption)
    {
        if ($consumption) {
            return 100 * ($amountIncome - $consumption) / $consumption;
        }

        return 0;
    }

    /**
     * Метод расчета Профит, profit. Разница между доходами с привлеченного трафика и расходами.
     *
     * @param float|int $amountIncome - сумма дохода
     * @param float|int $amountCost - сумма расхода
     * @return float|int
     */
    public function calculateProfit($amountIncome, $amountCost)
    {
        return $amountIncome - $amountCost;
    }

    /**
     * Метод расчета вовлеченности. (переходы на страницу полной новости с короткой / клики * 100%).
     *
     * @param int $shortToFullClick - количество переходов с короткой на полную новость
     * @param int $newsClick - количество кликов по рекламным блокам новостей
     * @return float|int
     */
    public function calculateInvolvement(int $shortToFullClick, int $newsClick)
    {
        if ($newsClick) {
            return  ($shortToFullClick / $newsClick) * 100;
        }

        return 0;
    }
}