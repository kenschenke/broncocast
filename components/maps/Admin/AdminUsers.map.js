import C from "../../../constants";
import { adminOrgChanged, approveUser, changeName, hideUnhideUser, removeUser } from '../../../actions/admin_users';

export const mapAdminUsersProps = state => {
    return {
        adminOrgId: state.admin_org.orgId,
        fetching: state.admin_users.fetching,
        showChangeNameDialog: state.admin_users.showNameDialog,
        showRemoveDialog: state.admin_users.showRemoveDialog,
        users: state.admin_users.users.filter(user => {
            if (user.Hidden) {
                return state.admin_users.showHidden;
            } else {
                return true;
            }
        })
    };
};

export const mapAdminUsersDispatch = dispatch => {
    return {
        adminOrgChanged() {
            dispatch(adminOrgChanged());
        },

        approveUser(MemberId) {
            dispatch(approveUser(MemberId));
        },

        changeNameCancel() {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showNameDialog: false
                }
            });
        },

        changeNameClicked(MemberId) {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showNameDialog: true,
                    nameChangeMemberId: MemberId
                }
            });
        },

        changeNameOk(Name) {
            dispatch(changeName(Name));
        },

        hideUnhideUser(MemberId) {
            dispatch(hideUnhideUser(MemberId));
        },

        init() {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showHidden: false
                }
            });
        },

        removeDialogNo() {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showRemoveDialog: false
                }
            });
        },

        removeDialogYes() {
            dispatch(removeUser());
        },

        removeUser(MemberId) {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showRemoveDialog: true,
                    removeMemberId: MemberId
                }
            });
        },

        setHidden(hidden) {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showHidden: hidden
                }
            });
        }
    };
};
