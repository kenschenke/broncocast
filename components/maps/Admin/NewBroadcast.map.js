import C from '../../../constants';
import { getFormFieldValue } from 'duxform';
import { saveNewBroadcast } from '../../../actions/admin_newbroadcast';

export const mapNewBroadcastProps = state => {
    const shortMsg = getFormFieldValue(state, 'broadcastmessage', 'shortmsg', '').trim();

    let validMsg = '&nbsp;';
    let valid = true;
    if (shortMsg.length < 1) {
        valid = false;
        validMsg = 'A short message is required';
    } else if (state.admin_newbroadcast.selected.length < 1) {
        valid = false;
        validMsg = 'At least one recipient must be selected';
    }

    return {
        isBroadcastValid: valid,
        saving: state.admin_newbroadcast.savingNewBroadcast,
        validMsg: validMsg
    };
};

export const mapNewBroadcastDispatch = dispatch => {
    return {
        cancelClicked() {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    show: false
                }
            });
        },

        okClicked() {
            dispatch(saveNewBroadcast());
        }
    };
};
