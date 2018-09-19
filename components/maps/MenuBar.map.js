import { initAdminOrg } from '../../actions/admin_menu';
import C from '../../constants';

export const mapMenuBarProps = state => {
    return {
        route: state.router.route,
        adminOrg: state.admin_org.orgId
    };
};

export const mapMenuBarDispatch = dispatch => {
    return {
        adminOrgClicked(OrgId, OrgName) {
            dispatch({
                type: C.SET_ADMIN_ORG,
                payload: {
                    orgId: OrgId,
                    orgName: OrgName
                }
            });
        },

        init() {
            if (window.AdminOrgs.length) {
                dispatch(initAdminOrg());
            }
        }
    };
};
