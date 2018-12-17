import $ from 'jquery';
import C from '../constants';

export const adminOrgChanged = () => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ROUTER_DATA,
        payload: {
            route: 'admin_broadcasts',
            name: `${state.admin_org.orgName} Broadcasts Admin`
        }
    });

    dispatch(getBroadcasts(state.admin_org.orgId));
};

export const cancelBroadcast = broadcastId => (dispatch, getState) => {
    const state = getState();

    $.ajax(`/api/admin/broadcasts/cancel/${broadcastId}`, {
        method: 'POST',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_BROADCASTS_DATA,
                payload: {
                    broadcasts: state.admin_broadcasts.broadcasts.map(broadcast => {
                        return {
                            ...broadcast,
                            IsCancelled: broadcastId === broadcast.BroadcastId ? true : broadcast.IsCancelled
                        };
                    })
                }
            });
        }
    });
};

export const getBroadcasts = () => (dispatch, getState) => {
    const state = getState();
    dispatch({
        type: C.SET_ADMIN_BROADCASTS_DATA,
        payload: {
            fetching: true
        }
    });

    $.get(`/api/admin/broadcasts/${state.admin_org.orgId}`, data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_ADMIN_BROADCASTS_DATA,
                payload: {
                    fetching: false
                }
            });
            alert(data.Error);
        }

        dispatch({
            type: C.SET_ADMIN_BROADCASTS_DATA,
            payload: {
                fetching: false,
                broadcasts: data.Broadcasts
            }
        });
    });
};
