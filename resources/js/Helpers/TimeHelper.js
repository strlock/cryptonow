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
}

export default TimeHelper;
