import { getUserOrgs } from '../../../actions/profile_userorgs';
import C from '../../../constants';

export const mapProfileOrgsProps = state => {
    return {
        orgs: state.profile_orgs.orgs,
        selectedOrgId: state.profile_orgs.selectedOrgMemberId
    };
};

export const mapProfileOrgsDispatch = dispatch => {
    return {
        deleteOrgClicked(memberId) {

        },

        init() {
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
