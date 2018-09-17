import $ from 'jquery';
import { getFormFieldValue, setFormFieldValue } from 'duxform';

export const getProfile = () => dispatch => {
    $.get('/api/profile', data => {
        if (!data.Success) {
            alert(data.Error);
            return;
        }

        dispatch(setFormFieldValue('profile_name', 'name', data.UsrName));
        dispatch(setFormFieldValue('profile_name', 'singlemsg', data.SingleMsg));
    });
};

export const updateProfile = singleMsg => (dispatch,getState) => {
    const state = getState();
    if (singleMsg === undefined) {
        singleMsg = getFormFieldValue(state, 'profile_name', 'singlemsg', false);
    }

    $.ajax('/api/profile', {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        data: {
            UsrName: getFormFieldValue(state, 'profile_name', 'name', ''),
            SingleMsg: singleMsg ? 'true' : 'false'
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
            }
        }
    });
};

