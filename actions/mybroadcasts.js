import $ from 'jquery';
import C from '../constants';

export const getBroadcasts = () => dispatch => {
    dispatch({
        type: C.SET_MYBROADCASTS_DATA,
        payload: {
            fetching: true
        }
    });

    $.get('/api/broadcasts', data => {
        if (!data.Success) {
            dispatch({
                type: C.SET_MYBROADCASTS_DATA,
                payload: {
                    fetching: false
                }
            });
            alert(data.Error);
        }

        dispatch({
            type: C.SET_MYBROADCASTS_DATA,
            payload: {
                fetching: false,
                broadcasts: data.Broadcasts
            }
        });
    });
};

