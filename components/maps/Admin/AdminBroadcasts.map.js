import C from '../../../constants';
import { adminOrgChanged } from '../../../actions/admin_broadcasts';

export const mapAdminBroadcastsProps = state => {
    return {
        adminOrgId: state.admin_org.orgId,
        fetching: state.admin_broadcasts.fetching,
        broadcasts: state.admin_broadcasts.broadcasts,
        showNewBroadcast: state.admin_newbroadcast.show
    };
};

export const mapAdminBroadcastsDispatch = dispatch => {
    return {
        adminOrgChanged() {
            dispatch(adminOrgChanged());
        },

        newBroadcastClicked() {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    show: true
                }
            });
        }
    };
};