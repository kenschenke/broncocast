import C from "../../../constants";
import { getBroadcasts } from '../../../actions/mybroadcasts';

export const mapMyBroadcastsProps = state => {
    return {
        fetching: state.mybroadcasts.fetching,
        broadcasts: state.mybroadcasts.broadcasts
    };
};

export const mapMyBroadcastsDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'broadcasts',
                    name: 'My Broadcasts'
                }
            });

            dispatch(getBroadcasts());
        }
    };
};
