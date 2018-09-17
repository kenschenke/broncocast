import C from '../../../constants';

export const mapProfileProps = state => {
    return {

    };
};

export const mapProfileDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'profile',
                    name: 'Profile'
                }
            });
        }
    };
};
