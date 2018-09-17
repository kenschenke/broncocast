import C from "../../../constants";

export const mapMyBroadcastsProps = state => {
    return {

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
        }
    };
};
