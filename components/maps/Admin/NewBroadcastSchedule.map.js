import { getTimezones } from '../../../actions/admin_newbroadcast';

export const mapNewBroadcastScheduledProps = state => {
    return {
        timezones: state.admin_newbroadcast.timezones,
        defaultTZ: state.admin_org.defaultTZ
    };
};

export const mapNewBroadcastScheduleDispatch = dispatch => {
    return {
        init() {
            dispatch(getTimezones());
        }
    };
};
