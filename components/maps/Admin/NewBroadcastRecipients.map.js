import C from '../../../constants';
import { getGroupMemberships, selectEveryone, selectGroupMembers, toggleUserSelected, unselectGroupMembers } from '../../../actions/admin_newbroadcast';

export const mapNewBroadcastRecipientsProps = state => {
    return {
        fetching: state.admin_newbroadcast.fetchingGroups,
        groups: state.admin_newbroadcast.groups,
        selected: state.admin_newbroadcast.selected
    };
};

export const mapNewBroadcastRecipientsDispatch = dispatch => {
    return {
        init() {
            dispatch(getGroupMemberships());
        },

        selectAllGroupMembers(Members) {
            dispatch(selectGroupMembers(Members));
        },

        selectEveryone() {
            dispatch(selectEveryone());
        },

        unselectAllGroupMembers(Members) {
            dispatch(unselectGroupMembers(Members));
        },

        unselectEveryone() {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    selected: []
                }
            });
        },

        userClicked(UserId) {
            dispatch(toggleUserSelected(UserId));
        }
    };
};
