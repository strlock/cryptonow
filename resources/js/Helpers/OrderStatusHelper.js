class OrderStateHelper
{
    static getNextOrderState(state, diff_percent)
    {
        switch (state) {
            case 'new': return 'ready';
            case 'ready': return diff_percent >= 0 ? 'profit' : 'loss';
            case 'profit': return 'completed';
            case 'loss':  return 'loss';
            case 'failed': return 'failed';
            case 'canceled': return 'canceled';
            case 'completed': return 'completed';
            default: return 'new';
        }
    }
}

export default OrderStateHelper;
