class FormatHelper
{
    static fromSign = null
    static toSign = null

    static formatPrice(price, checkNull, sign)
    {
        if (checkNull && price === null) {
            return '-';
        }
        if (sign === undefined) {
            sign = this.toSign;
        }
        return this.formatFloat(price, 2) + (sign ? sign : '');
    }

    static formatAmount(amount, checkNull, sign)
    {
        if (checkNull && amount === null) {
            return '-';
        }
        if (sign === undefined) {
            sign = this.fromSign;
        }
        return this.formatFloat(amount, 5) + (sign ? sign : '');
    }

    static formatFloat(value, digits)
    {
        value = parseFloat(value);
        if (isNaN(value)) {
            value = 0.0;
        }
        return value.toFixed(digits);
    }

    static formatDate(date)
    {
        return date !== null ? (new Date(date)).toLocaleString() : '-';
    }

    static setFromSign(sign)
    {
        this.fromSign = sign;
    }

    static setToSign(sign)
    {
        this.toSign = sign;
    }
}

export default FormatHelper;
