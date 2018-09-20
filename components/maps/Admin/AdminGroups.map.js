import C from '../../../constants';
import { adminOrgChanged, changeName, newGroup, removeGroup } from '../../../actions/admin_groups';
import { isFieldValid } from 'duxform';

export const mapAdminGroupsProps = state => {
    return {
        adminOrgId: state.admin_org.orgId,
        fetching: state.admin_groups.fetching,
        groups: state.admin_groups.groups,
        showChangeNameDialog: state.admin_groups.showNameDialog,
        showRemoveDialog: state.admin_groups.showRemoveDialog,
        newGroupNameValid: isFieldValid(state, 'newgroup', 'name')
    };
};

export const mapAdminGroupsDispatch = dispatch => {
    return {
        addGroupClicked() {
            dispatch(newGroup());
        },

        adminOrgChanged() {
            dispatch(adminOrgChanged());
        },

        changeNameCancel() {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showNameDialog: false
                }
            });
        },

        changeNameClicked(GroupId) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showNameDialog: true,
                    nameChangeGroupId: GroupId
                }
            });
        },

        changeNameOk(Name) {
            dispatch(changeName(Name));
        },

        removeDialogNo() {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showRemoveDialog: false
                }
            });
        },

        removeDialogYes() {
            dispatch(removeGroup());
        },

        removeGroup(GroupId) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showRemoveDialog: true,
                    removeGroupId: GroupId
                }
            });
        }
    };
};
