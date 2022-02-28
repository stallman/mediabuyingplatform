const date_object = {
    1: 'January',
    2: 'February',
    3: 'March',
    4: 'April',
    5: 'May',
    6: 'June',
    7: 'July',
    8: 'August',
    9: 'September',
    10: 'October',
    11: 'November',
    12: 'December',
}

const date_object_to_rus = {
    1: 'Января',
    2: 'Февраля',
    3: 'Марта',
    4: 'Апреля',
    5: 'Мая',
    6: 'Июня',
    7: 'Июля',
    8: 'Августа',
    9: 'Сентября',
    10: 'Октября',
    11: 'Ноября',
    12: 'Декабря',
}

function compareDates(day_in_block, month_in_block, is_digit=false) {
    let current_date = new Date();
    let current_month = date_object_to_rus[current_date.getMonth() + 1];
    let current_day = current_date.getDate();
    let is_one_digit = false;

    if (day_in_block.charAt(0) === '0') {
        is_one_digit = true;
        day_in_block = day_in_block.charAt(1);
    }
    day_in_block = parseInt(day_in_block)

    if (current_day >= day_in_block) {
        if (is_one_digit) {
            day_in_block = '0' + day_in_block;
        }
        if (!is_digit) {
            return {
                day: day_in_block,
                month: current_month
            }
        } else {
            let target_month = current_date.getMonth() + 1;
            target_month = target_month.toString();
            if(target_month.length === 1) {
                target_month = '0' + target_month;
            }
            return {
                day: day_in_block,
                month: target_month
            }
        }
    }

    if (is_one_digit) {
        day_in_block = '0' + day_in_block;
    }

    if (is_digit) {
        let target_month = month_in_block;
        target_month = target_month.toString();
        if(target_month.length === 1) {
            target_month = '0' + target_month;
        }
        return {
            day: day_in_block,
            month: target_month
        }
    }
    return {
        day: day_in_block,
        month: date_object_to_rus[month_in_block]
    }
}

function convertMonthToNumber(month) {
    let result;
    for(let i in date_object) {
        if (date_object[i] === month) {
            result = i;
            break;
        }
    }
    return result;
}