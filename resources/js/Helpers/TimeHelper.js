import TimeIntervals from "../TimeIntervals";

class TimeHelper
{
    static subDaysFromDate(date, days)
    {
        let time = date.getTime();
        time -= Math.round(TimeIntervals.ONE_DAY*days);
        date.setTime(time);
        return date;
    }

    static daysForInterval(interval)
    {
        return interval/TimeIntervals.FIVE_MINUTES;
    }

    static round(timestamp, interval)
    {
        return interval*Math.floor(timestamp/interval);
    }

    static getTimeRangeForInterval(interval)
    {
        const daysForInterval = TimeHelper.daysForInterval(interval)
        return [
            TimeHelper.round((TimeHelper.subDaysFromDate(new Date(), daysForInterval)).getTime(), interval),
            TimeHelper.round((new Date()).getTime(), interval),
        ];
    }
}

export default TimeHelper;
