import { addOrg, deleteUserOrg, getUserOrgs } from '../../../actions/profile_userorgs';
import { getFormFieldValue } from 'duxform';
import C from '../../../constants';

export const mapProfileOrgsProps = state => {
    const tag = getFormFieldValue(state, 'userorgs', 'tag', '').trim();
    return {
        joinDisabled: tag.length < 1,
        orgs: state.profile_orgs.orgs,
        orgMessage: state.profile_orgs.orgMessage,
        selectedOrgId: state.profile_orgs.selectedOrgMemberId
    };
};

export const mapProfileOrgsDispatch = dispatch => {
    return {
        addOrg() {
            dispatch(addOrg());
        },

        deleteOrgClicked(memberId) {
            dispatch(deleteUserOrg(memberId));
        },

        init() {
            dispatch({
                type: C.SET_PROFILE_ORGS_DATA,
                payload: {
                    orgMessage: ''
                }
            });
            dispatch(getUserOrgs());
        },

        orgSelected(memberId) {
            dispatch({
                type: C.SET_PROFILE_ORGS_DATA,
                payload: {
                    selectedOrgMemberId: memberId
                }
            });
        }
    };
};
