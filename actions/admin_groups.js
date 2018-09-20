import $ from 'jquery';
import C from '../constants';
import { getFormFieldValue, setFormFieldValue } from 'duxform';

export const addGroupMember = (GroupId, UserId, UserName) => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/groups/members/${GroupId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'POST',
        data: {
            UserId: UserId
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    members: [
                        ...state.admin_groups.members,
                        {
                            MemberId: data.MemberId,
                            UserId: UserId,
                            UserName: UserName
                        }
                    ],
                    nonMembers: state.admin_groups.nonMembers.filter(user => user.UserId !== UserId)
                }
            });
        }
    });
};

export const adminOrgChanged = () => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ROUTER_DATA,
        payload: {
            route: 'admin_groups',
            name: `${state.admin_org.orgName} Groups Admin`
        }
    });

    dispatch(getGroups(state.admin_org.orgId));
};

export const changeName = Name => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/groups/name/${state.admin_groups.nameChangeGroupId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        data: {
            Name: Name
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showNameDialog: false,
                    nameChangeGroupId: 0,
                    groups: state.admin_groups.groups.map(group => {
                        return {
                            ...group,
                            GroupName: group.GroupId === state.admin_groups.nameChangeGroupId ? Name : group.GroupName
                        };
                    })
                }
            });
        }
    });
};

export const getGroupMembers = GroupId => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ADMIN_GROUPS_DATA,
        payload: {
            fetchingMembers: true
        }
    });

    $.get(`/api/admin/groups/members/${GroupId}`, data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    fetchingMembers: false
                }
            });
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_ADMIN_GROUPS_DATA,
            payload: {
                fetchingMembers: false,
                members: data.Members
            }
        });
    });
};

export const getGroupNonMembers = GroupId => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ADMIN_GROUPS_DATA,
        payload: {
            fetchingNonMembers: true
        }
    });

    $.get(`/api/admin/groups/nonmembers/${GroupId}`, data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    fetchingNonMembers: false
                }
            });
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_ADMIN_GROUPS_DATA,
            payload: {
                fetchingNonMembers: false,
                nonMembers: data.NonMembers
            }
        });
    });
};

export const getGroups = OrgId => dispatch => {
    dispatch({
        type: C.SET_ADMIN_GROUPS_DATA,
        payload: {
            fetching: true
        }
    });

    $.get(`/api/admin/groups/${OrgId}`, data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    fetching: true
                }
            });
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_ADMIN_GROUPS_DATA,
            payload: {
                fetching: false,
                groups: data.Groups
            }
        });
    });
};

export const newGroup = () => (dispatch, getState) => {
    const state = getState();
    const Name = getFormFieldValue(state, 'newgroup', 'name', '');

    $.ajax(`/api/admin/groups/${state.admin_org.orgId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'POST',
        data: {
            Name: Name
        },
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showNameDialog: false,
                    nameChangeGroupId: 0,
                    groups: [
                        ...state.admin_groups.groups,
                        {
                            GroupId: data.GroupId,
                            GroupName: Name
                        }
                    ]
                }
            });

            dispatch(setFormFieldValue('newgroup', 'name', ''));
        }
    });
};

export const removeGroup = () => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/groups/remove/${state.admin_groups.removeGroupId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    showRemoveDialog: false,
                    groups: state.admin_groups.groups.filter(group => group.GroupId !== state.admin_groups.removeGroupId)
                }
            });
        }
    });
};

export const removeGroupMember = (MemberId, UserId, UserName) => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/groups/members/${MemberId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_GROUPS_DATA,
                payload: {
                    nonMembers: [
                        ...state.admin_groups.nonMembers,
                        {
                            UserId: UserId,
                            UserName: UserName
                        }
                    ],
                    members: state.admin_groups.members.filter(member => member.MemberId !== MemberId)
                }
            });
        }
    });
};

