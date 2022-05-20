import { useContext } from 'react';
import {stateContext} from "../components/StateProvider";

const usePopup = () => {
    const [state, actions] = useContext(stateContext);
    return {
        show: (message, type, title) => {
            actions.setPopup({
                show: true,
                type: type,
                message: message,
                title: title,
            });
            clearTimeout(popupTimeout.current);
            popupTimeout.current = setTimeout(function() {
                actions.resetPopup();
            }, POPUP_TIMEOUT);
        },
        hide: () => {
            actions.resetPopup();
        }
    }
}

export default usePopup
