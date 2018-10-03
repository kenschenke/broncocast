import C from "../../../constants";
import { adminOrgChanged, approveUser, changeName, hideUnhideUser, removeUser } from '../../../actions/admin_users';

export const mapAdminUsersProps = state => {
    let numHiddenUsers = 0;
    let numUnapprovedUsers = 0;
    let numDeliveryProblems = 0;

    const users = state.admin_users.users.filter(user => {
        if (user.Hidden) {
            numHiddenUsers++;
        }
        if (!user.Approved) {
            numUnapprovedUsers++;
        }
        if (user.HasDeliveryError) {
            numDeliveryProblems++;
        }

        switch (state.admin_users.filterOn) {
            case 'showhidden': return true;
            case 'hidehidden': return !user.Hidden;
            case 'onlyunapproved': return !user.Approved;
            case 'onlydeliveryproblems': return user.HasDeliveryError;
        }
    });

    const hiddenUsers = (numHiddenUsers === 0) ?
        'No hidden users' :
        (numHiddenUsers.toString() + ' hidden user' + (numHiddenUsers === 1 ? '' : 's'));
    const unapprovedUsers = (numUnapprovedUsers === 0) ?
        'No unapproved users' :
        (numUnapprovedUsers.toString() + ' unapproved user' + (numUnapprovedUsers === 1 ? '' : 's'));
    const deliveryProblems = (numDeliveryProblems === 0) ?
        'No users with delivery problems' :
        (numDeliveryProblems.toString() + ' user' + (numDeliveryProblems === 1 ? '' : 's') + ' with delivery problems');

    return {
        filterOn: state.admin_users.filterOn,
        adminOrgId: state.admin_org.orgId,
        fetching: state.admin_users.fetching,
        showChangeNameDialog: state.admin_users.showNameDialog,
        showRemoveDialog: state.admin_users.showRemoveDialog,
        users: users,
        hiddenUsers: hiddenUsers,
        unapprovedUsers: unapprovedUsers,
        unapprovedUsersClass: 'badge mr-2 ' + (numUnapprovedUsers > 0 ? 'badge-warning' : 'badge-secondary'),
        deliveryProblems: deliveryProblems,
        deliveryProblemsClass: 'badge mr-2 ' + (numDeliveryProblems > 0 ? 'badge-danger' : 'badge-secondary')
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
                    filterOn: 'hidehidden'
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

        setFilterOn(filterOn) {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    filterOn: filterOn
                }
            });
        }
    };
};
