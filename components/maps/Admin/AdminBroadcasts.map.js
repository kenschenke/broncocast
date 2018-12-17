import C from '../../../constants';
import { adminOrgChanged, cancelBroadcast } from '../../../actions/admin_broadcasts';

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

        cancelBroadcastClicked(broadcastId) {
            dispatch(cancelBroadcast(broadcastId));
        },

        newBroadcastClicked() {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    show: true,
                    savingNewBroadcast: false,
                    fetchingGroups: false,
                    uploadingAttachment: false,
                    attachmentSuccess: false,
                    attachmentFriendlyName: '',
                    attachmentLocalName: '',
                    attachmentMimeType: '',
                    groups: {},
                    selected: [],
                    timezones: []
                }
            });
        }
    };
};
