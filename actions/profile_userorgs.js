import C from '../constants';
import $ from 'jquery';

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
