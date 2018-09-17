import C from "../../../constants";

export const mapAdminProps = state => {
    return {

    };
};

export const mapAdminDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'admin',
                    name: 'Admin'
                }
            });
        }
    };
};

