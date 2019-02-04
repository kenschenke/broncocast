import C from '../../../constants';
import { deleteUser, fillUserName, getSystemUsers } from '../../../actions/system_users';

export const mapSystemUsersProps = state => {
    return {
        users: state.system_users.users,
        fetching: state.system_users.fetching,
        showDeleteDialog: state.system_users.showDeleteDialog,
        deleteUserId: state.system_users.deleteUserId
    };
};

export const mapSystemUsersDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'system_users',
                    name: 'System Users Admin'
                }
            });

            dispatch(getSystemUsers());
        },

        deleteUserClicked(userId) {
            dispatch({
                type: C.SET_SYSTEM_USERS_DATA,
                payload: {
                    showDeleteDialog: true,
                    deleteUserId: userId
                }
            });
        },

        deleteNoClicked() {
            dispatch({
                type: C.SET_SYSTEM_USERS_DATA,
                payload: {
                    showDeleteDialog: false
                }
            });
        },

        deleteYesClicked() {
            dispatch(deleteUser());
        },

        fillNameClicked(fillMemberId) {
            dispatch(fillUserName(fillMemberId));
        }
    };
};
