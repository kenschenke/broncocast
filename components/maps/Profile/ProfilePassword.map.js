import { getFormFieldValue, isFieldValidOrPristine, isFormValid } from 'duxform';
import { changePassword, checkCurrentPwd } from '../../../actions/profile_pwd';
import C from '../../../constants';

export const mapProfilePasswordProps = state => {
    return {
        formValid: isFormValid(state, 'profilepwd') && !state.profile_pwd.validatingCurrentPwd && state.profile_pwd.currentPwdValid,
        currentPwd: getFormFieldValue(state, 'profilepwd', 'currentpwd', ''),
        currentPwdValid: state.profile_pwd.currentPwdValid,
        validatingCurrentPwd: state.profile_pwd.validatingCurrentPwd,
        newPwd1: getFormFieldValue(state, 'profilepwd', 'newpwd1', ''),
        newPwd1Valid: isFieldValidOrPristine(state, 'profilepwd', 'newpwd1'),
        newPwd2Valid: isFieldValidOrPristine(state, 'profilepwd', 'newpwd2'),
    };
};

export const mapProfilePasswordDispatch = dispatch => {
    return {
        changePassword(pwd) {
            dispatch(changePassword(pwd));
        },

        checkCurrentPwd(pwd) {
            dispatch(checkCurrentPwd(pwd));
        },

        init() {
            dispatch({
                type: C.SET_PROFILE_PWD_DATA,
                payload: {
                    currentPwdValid: true,
                    validatingCurrentPwd: false
                }
            });
        }
    };
};
