import C from '../constants';
import $ from 'jquery';
import { getFormFieldValue } from 'duxform';

export const getGroupMemberships = () => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ADMIN_NEWBROADCAST_DATA,
        payload: {
            fetchingGroups: true,
            selected: []
        }
    });

    $.get(`/api/admin/broadcasts/groups/${state.admin_org.orgId}`, data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    fetchingGroups: false
                }
            });
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_ADMIN_NEWBROADCAST_DATA,
            payload: {
                fetchingGroups: false,
                groups: data.Groups
            }
        });
    });
};

export const getTimezones = () => dispatch => {
    $.get('/api/timezones', data => {
        if (!data.Success) {
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_ADMIN_NEWBROADCAST_DATA,
            payload: {
                timezones: data.Timezones
            }
        });
    });
};

export const saveNewBroadcast = () => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ADMIN_NEWBROADCAST_DATA,
        payload: { savingNewBroadcast: true }
    });

    let params = {
        ShortMsg: getFormFieldValue(state, 'broadcastmessage', 'shortmsg', ''),
        LongMsg: getFormFieldValue(state, 'broadcastmessage', 'longmsg', ''),
        Recipients: state.admin_newbroadcast.selected.join(','),
        Scheduled: getFormFieldValue(state, 'broadcastschedule', 'schedule', ''),
        TimeZone: getFormFieldValue(state, 'broadcastschedule', 'timezone', ''),
        AttachmentFriendlyName: state.admin_newbroadcast.attachmentFriendlyName,
        AttachmentLocalName: state.admin_newbroadcast.attachmentLocalName,
        AttachmentMimeType: state.admin_newbroadcast.attachmentMimeType
    };

    $.ajax(`/api/admin/broadcasts/new/${state.admin_org.orgId}`, {
        method: 'POST',
        contentType: 'application/x-www-form-urlencoded',
        data: params,
        success: data => {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: { savingNewBroadcast: false }
            });

            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    show: false
                }
            });

            dispatch({
                type: C.SET_ADMIN_BROADCASTS_DATA,
                payload: {
                    broadcasts: [
                        ...state.admin_broadcasts.broadcasts,
                        {
                            BroadcastId: data.BroadcastId,
                            ShortMsg: data.ShortMsg,
                            LongMsg: data.LongMsg,
                            Time: data.Time,
                            Timestamp: data.Timestamp,
                            IsDelivered: data.IsDelivered,
                            IsCancelled: false,
                            UsrName: data.UsrName,
                            AttachmentUrl: '',
                            Recipients: data.Recipients,
                        }
                    ]
                }
            });
        }
    });
};

export const selectEveryone = () => (dispatch, getState) => {
    const state = getState();
    let selected = [];

    for (let group in state.admin_newbroadcast.groups) {
        if (state.admin_newbroadcast.groups.hasOwnProperty(group)) {
            for (let i = 0; i < state.admin_newbroadcast.groups[group].length; i++) {
                if (selected.indexOf(state.admin_newbroadcast.groups[group][i].UserId) === -1) {
                    selected.push(state.admin_newbroadcast.groups[group][i].UserId);
                }
            }
        }
    }

    dispatch({
        type: C.SET_ADMIN_NEWBROADCAST_DATA,
        payload: {
            selected: selected
        }
    });
};

export const selectGroupMembers = Members => (dispatch, getState) => {
    const state = getState();

    const newSelected = [...state.admin_newbroadcast.selected];
    for (let i = 0; i < Members.length; i++) {
        if (newSelected.indexOf(Members[i]) === -1) {
            newSelected.push(Members[i]);
        }
    }

    dispatch({
        type: C.SET_ADMIN_NEWBROADCAST_DATA,
        payload: {
            selected: newSelected
        }
    });
};

export const toggleUserSelected = UserId => (dispatch, getState) => {
    const state = getState();
    if (state.admin_newbroadcast.selected.indexOf(UserId) !== -1) {
        // user is currently selected - remove them
        dispatch({
            type: C.SET_ADMIN_NEWBROADCAST_DATA,
            payload: {
                selected: state.admin_newbroadcast.selected.filter(id => id !== UserId)
            }
        });
    } else {
        // user is not currently selected - add them
        dispatch({
            type: C.SET_ADMIN_NEWBROADCAST_DATA,
            payload: {
                selected: [...state.admin_newbroadcast.selected, UserId]
            }
        });
    }
};


export const unselectGroupMembers = Members => (dispatch, getState) => {
    const state = getState();

    dispatch({
        type: C.SET_ADMIN_NEWBROADCAST_DATA,
        payload: {
            selected: state.admin_newbroadcast.selected.filter(member => Members.indexOf(member) === -1)
        }
    });
};

export const uploadAttachment = friendlyName => dispatch => {
    dispatch({
        type: C.SET_ADMIN_NEWBROADCAST_DATA,
        payload: {
            uploadingAttachment: true
        }
    });

    const myFormData = new FormData();
    myFormData.append('attachment', document.forms.attachment.uploadfile.files[0]);

    $.ajax('/api/admin/broadcasts/attachment', {
        method: 'POST',
        data: myFormData,
        processData: false,
        contentType: false,
        success: data => {
            if (!data.Success) {
                dispatch({
                    type: C.SET_ADMIN_NEWBROADCAST_DATA,
                    payload: {
                        uploadingAttachment: false
                    }
                });
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    uploadingAttachment: false,
                    attachmentSuccess: true,
                    attachmentFriendlyName: friendlyName,
                    attachmentLocalName: data.LocalName,
                    attachmentMimeType: data.MimeType
                }
            });
        }
    });
};
