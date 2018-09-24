import { isFieldValidOrPristine, isFormValid } from 'duxform';
import { saveOrg } from '../../../actions/system_orgs';
import C from '../../../constants';

export const mapEditOrgProps = state => {
    return {
        defaultTZ: state.system_orgs.editingDefaultTZ,
        formValid: isFormValid(state, 'editorg'),
        orgName: state.system_orgs.editingOrgName,
        orgNameValid: isFieldValidOrPristine(state, 'editorg', 'orgname'),
        show: state.system_orgs.showEditDialog,
        tag: state.system_orgs.editingTag,
        timezones: state.system_orgs.timezones,
    };
};

export const mapEditOrgDispatch = dispatch => {
    return {
        cancelClicked() {
            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    showEditDialog: false
                }
            });
        },

        okClicked() {
            dispatch(saveOrg());
        }
    };
};
