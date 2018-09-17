import $ from 'jquery';
import C from '../constants';

export const changePassword = password => dispatch => {
    $.ajax('/api/profile/password', {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        data: {
            Password: password
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                // return;
            }
        }
    });
};

export const checkCurrentPwd = currentPwd => dispatch => {
    dispatch({
        type: C.SET_PROFILE_PWD_DATA,
        payload: {
            validatingCurrentPwd: true
        }
    });

    $.ajax('/api/profile/currentpwd', {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        data: {
            Password: currentPwd
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_PROFILE_PWD_DATA,
                payload: {
                    validatingCurrentPwd: false,
                    currentPwdValid: data.Valid
                }
            });
        }
    });
};

