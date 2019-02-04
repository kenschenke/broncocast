import $ from 'jquery';
import C from '../constants';

export const deleteUser = () => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/system/users/${state.system_users.deleteUserId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_SYSTEM_USERS_DATA,
                payload: {
                    users: state.system_users.users.filter(user => user.UserId !== state.system_users.deleteUserId),
                    showDeleteDialog: false,
                    deleteUserId: 0
                }
            });
        }
    });
};

export const fillUserName = memberId => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/system/users/fillname/${memberId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'PUT',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_SYSTEM_USERS_DATA,
                payload: {
                    users: state.system_users.users.map(user => {
                        if (memberId === user.FillMemberId) {
                            return {
                                ...user,
                                FillMemberId: 0,
                                UserName: data.UserName
                            };
                        } else {
                            return user;
                        }
                    })
                }
            });
        }
    });
};

export const getSystemUsers = () => dispatch => {
    dispatch({
        type: C.SET_SYSTEM_USERS_DATA,
        payload: {
            fetching: true
        }
    });

    $.get("/api/system/users", data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_SYSTEM_USERS_DATA,
                payload: {
                    fetching: false
                }
            });

            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_SYSTEM_USERS_DATA,
            payload: {
                fetching: false,
                users: data.Users
            }
        });
    });
};
