import C from '../constants';
import $ from 'jquery';
import { getFormFieldValue, setFormFieldValue } from 'duxform';

export const addOrg = () => (dispatch, getState) => {
    const state = getState();
    const Tag = getFormFieldValue(state, 'userorgs', 'tag', '');
    $.ajax('/api/orgs', {
        method: 'POST',
        contentType: 'application/x-www-form-urlencoded',
        data: {
            Tag: Tag
        },
        success: data => {
            if (!data.Success) {
                dispatch({
                    type: C.SET_PROFILE_ORGS_DATA,
                    payload: {
                        orgMessage: data.Error
                    }
                });
                return;
            }

            dispatch({
                type: C.SET_PROFILE_ORGS_DATA,
                payload: {
                    orgMessage: '',
                    orgs: [...state.profile_orgs.orgs, {
                        MemberId: data.MemberId,
                        OrgId: data.OrgId,
                        OrgName: data.OrgName,
                        IsAdmin: data.IsSystemAdmin
                    }]
                }
            });
            dispatch(setFormFieldValue('userorgs', 'tag', '', false));
        }
    });
};

export const deleteUserOrg = MemberId => (dispatch, getState) => {
    const state = getState();
    $.ajax(`/api/orgs/${MemberId}`, {
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_PROFILE_ORGS_DATA,
                payload: {
                    orgs: state.profile_orgs.orgs.filter(org => org.MemberId !== MemberId)
                }
            });
        }
    });
};

export const getUserOrgs = () => dispatch => {
    $.get('/api/orgs', data => {
        if (!data.Success) {
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_PROFILE_ORGS_DATA,
            payload: {
                orgs: data.Orgs,
                adminOrgs: data.AdminOrgs
            }
        });
    });
};
