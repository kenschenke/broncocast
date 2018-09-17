import { getProfile, updateProfile } from '../../../actions/profile_name';

export const mapProfileNameProps = state => {
    return {
    };
};

export const mapProfileNameDispatch = dispatch => {
    return {
        init() {
            dispatch(getProfile());
        },

        singleMsgClicked(singleMsg) {
            dispatch(updateProfile(singleMsg));
        },

        updateClicked() {
            dispatch(updateProfile());
        }
    };
};
