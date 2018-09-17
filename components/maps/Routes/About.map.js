import C from "../../../constants";

export const mapAboutProps = state => {
    return {

    };
};

export const mapAboutDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'about',
                    name: 'About'
                }
            });
        }
    };
};
