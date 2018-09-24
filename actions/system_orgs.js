import $ from 'jquery';
import C from '../constants';
import { getFormFieldValue, setFormFieldValue } from 'duxform';

export const deleteOrg = () => (dispatch, getState) => {
    const state = getState();
    $.ajax(`/api/system/orgs/${state.system_orgs.selectedOrgId}`, {
        contentType: 'application/x-www-form-urlencoded',
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    showDeleteDialog: false,
                    orgs: state.system_orgs.orgs.filter(org => org.OrgId !== state.system_orgs.selectedOrgId)
                }
            });
        }
    });
};

export const editOrg = () => (dispatch, getState) => {
    const state = getState();

    $.get(`/api/system/orgs/${state.system_orgs.selectedOrgId}`, data => {
        if (!data.Success) {
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_SYSTEM_ORGS_DATA,
            payload: {
                editingDefaultTZ: data.DefaultTZ,
                editingOrgId: state.system_orgs.selectedOrgId,
                editingOrgName: data.OrgName,
                showEditDialog: true,
                editingTag: data.Tag
            }
        });
    });
};

export const getOrgs = () => dispatch => {
    dispatch({
        type: C.SET_SYSTEM_ORGS_DATA,
        payload: {
            fetching: true
        }
    });

    $.get('/api/system/orgs', data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    fetching: false
                }
            });

            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_SYSTEM_ORGS_DATA,
            payload: {
                fetching: false,
                orgs: data.Orgs
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
            type: C.SET_SYSTEM_ORGS_DATA,
            payload: {
                timezones: data.Timezones
            }
        });
    });
};

export const saveOrg = () => (dispatch, getState) => {
    const state = getState();
    if (state.system_orgs.editingOrgId) {
        const OrgName = getFormFieldValue(state, 'editorg', 'orgname', '');
        $.ajax(`/api/system/orgs/${state.system_orgs.editingOrgId}`, {
            contentType: 'application/x-www-form-urlencoded',
            method: 'PUT',
            data: {
                OrgName: OrgName,
                DefaultTZ: getFormFieldValue(state, 'editorg', 'defaulttz', ''),
                Tag: getFormFieldValue(state, 'editorg', 'tag', '')
            },
            success: data => {
                if (!data.Success) {
                    alert(data.Error);
                    return;
                }

                dispatch({
                    type: C.SET_SYSTEM_ORGS_DATA,
                    payload: {
                        showEditDialog: false,
                        orgs: state.system_orgs.orgs.map(org => {
                            return {
                                ...org,
                                OrgName: org.OrgId === state.system_orgs.editingOrgId ? OrgName : org.OrgName
                            };
                        })
                    }
                });
            }
        });
    } else {
        const OrgName = getFormFieldValue(state, 'editorg', 'orgname', '');
        $.ajax('/api/system/orgs', {
            contentType: 'application/x-www-form-urlencoded',
            method: 'POST',
            data: {
                OrgName: OrgName,
                DefaultTZ: getFormFieldValue(state, 'editorg', 'defaulttz', ''),
                Tag: getFormFieldValue(state, 'editorg', 'tag', '')
            },
            success: data => {
                if (!data.Success) {
                    alert(data.Error);
                    return;
                }

                dispatch({
                    type: C.SET_SYSTEM_ORGS_DATA,
                    payload: {
                        showEditDialog: false,
                        orgs: [
                            ...state.system_orgs.orgs,
                            {
                                OrgId: data.OrgId,
                                OrgName: OrgName
                            }
                        ]
                    }
                });
            }
        });
    }
};
