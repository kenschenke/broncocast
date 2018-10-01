import C from '../../../constants';
import { getFormFieldValue } from 'duxform';
import $ from 'jquery';

const saveName = () => (dispatch, getState) => {
    const state = getState();

    const username = getFormFieldValue(state, 'register_name', 'username', '').trim();
    const singleMsg = getFormFieldValue(state, 'register_name', 'singlemsg', false);

    if (username.length < 1) {
        alert('Please enter your name to continue');
        return;
    }

    $.ajax('/api/profile', {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        data: {
            UsrName: username,
            SingleMsg: singleMsg ? 'true' : 'false'
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_REGISTER_DATA,
                payload: {
                    step: 'contacts'
                }
            });
        }
    });
};

export const mapRegisterProps = state => {
    return {
        step: state.register.step
    };
};

export const mapRegisterDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_REGISTER_DATA,
                payload: {
                    step: 'name'
                }
            });
        },

        nextContactClicked() {
            $.ajax('/api/register/welcome', {
                contentType: 'application/x-www-form-urlencoded',
                method: 'PUT',
                success: data => {
                    if (!data.Success) {
                        alert(data.Error);
                    }
                }
            });
        },

        nextNameClicked() {
            dispatch(saveName());
        }
    };
};
