import C from "../../../constants";

export const mapSystemProps = state => {
    return {

    };
};

export const mapSystemDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'system',
                    name: 'System'
                }
            });
        }
    };
};
