import C from '../constants';

export const initAdminOrg = () => dispatch => {
    // Look for the first org in AdminOrgs that the user is a member of

    for (let a = 0; a < window.AdminOrgs.length; a++) {
        if (window.AdminOrgs[a].AdminDefault) {
            dispatch({
                type: C.SET_ADMIN_ORG,
                payload: {
                    orgId: window.AdminOrgs[a].OrgId,
                    orgName: window.AdminOrgs[a].OrgName,
                    defaultTZ: window.AdminOrgs[a].DefaultTZ
                }
            });

            return;
        }
    }
};
