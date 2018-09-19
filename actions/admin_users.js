import $ from 'jquery';
import C from '../constants';

export const adminOrgChanged = () => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ROUTER_DATA,
        payload: {
            route: 'admin_users',
            name: `${state.admin_org.orgName} Admin`
        }
    });
    dispatch(getUsers(state.admin_org.orgId));
};

export const approveUser = MemberId => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/users/approve/${MemberId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    users: state.admin_users.users.map(user => {
                        return {
                            ...user,
                            Approved: user.MemberId === MemberId ? true : user.Approved
                        };
                    })
                }
            });
        }
    });
};

export const changeName = Name => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/users/name/${state.admin_users.nameChangeMemberId}`, {
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
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    showNameDialog: false,
                    nameChangeMemberId: 0,
                    users: state.admin_users.users.map(user => {
                        return {
                            ...user,
                            UsrName: user.MemberId === state.admin_users.nameChangeMemberId ? Name : user.UsrName
                        };
                    })
                }
            });
        }
    });
};

export const getUsers = OrgId => dispatch => {
    dispatch({
        type: C.SET_ADMIN_USERS_DATA,
        payload: {
            fetching: true
        }
    });

    $.get(`/api/admin/users/${OrgId}`, data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    fetching: false
                }
            });
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_ADMIN_USERS_DATA,
            payload: {
                fetching: false,
                users: data.Users
            }
        });
    });
};

export const hideUnhideUser = MemberId => (dispatch, getState) => {
    const state = getState();

    const user = state.admin_users.users.filter(user => user.MemberId === MemberId);
    if (user.length !== 1) {
        return;
    }
    const url = user[0].Hidden ? `/api/admin/users/unhide/${MemberId}` : `/api/admin/users/hide/${MemberId}`;
    $.ajax(url, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    users: state.admin_users.users.map(user => {
                        return {
                            ...user,
                            Hidden: user.MemberId === MemberId ? !user.Hidden : user.Hidden
                        };
                    })
                }
            });
        }
    });
};

export const removeUser = () => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/users/remove/${state.admin_users.removeMemberId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_USERS_DATA,
                payload: {
                    users: state.admin_users.users.filter(user => user.MemberId !== state.admin_users.removeMemberId),
                    showRemoveDialog: false
                }
            });
        }
    });
};
