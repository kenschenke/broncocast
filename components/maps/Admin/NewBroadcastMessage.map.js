import { getFormFieldValue } from 'duxform';

export const mapNewBroadcastMessageProps = state => {
    const shortMsg = getFormFieldValue(state, 'broadcastmessage', 'shortmsg', '').trim();
    return {
        charsLeft: 127 - shortMsg.length
    };
};

export const mapNewBroadcastMessageDispatch = dispatch => {
    return {

    };
};
