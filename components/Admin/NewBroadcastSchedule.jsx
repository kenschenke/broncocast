import React from 'react';
import PropTypes from 'prop-types';
import { mapNewBroadcastScheduledProps, mapNewBroadcastScheduleDispatch } from '../maps/Admin/NewBroadcastSchedule.map';
import { connect } from 'react-redux';
import { DuxForm, DuxInput } from 'duxform';

class NewBroadcastScheduleUi extends React.Component {
    componentDidMount() {
        this.props.init();
    }

    render() {
        const zones = this.props.timezones.map(zone => <option key={zone} value={zone}>{zone}</option>);

        return (
            <DuxForm name="broadcastschedule">
                <div className="form-group">
                    <label>Scheduled Delivery (MM/DD/YYYY HH:MM:SS)</label>
                    <DuxInput name="schedule"
                              placeholder="blank for immediate delivery"
                              className="form-control"
                    />
                </div>
                <div className="form-group">
                    <label>Time Zone</label>
                    <DuxInput name="timezone" type="select" className="form-control" defaultValue={this.props.defaultTZ}>
                        {zones}
                    </DuxInput>
                </div>
            </DuxForm>
        );
    }
}

NewBroadcastScheduleUi.propTypes = {
    defaultTZ: PropTypes.string.isRequired,
    timezones: PropTypes.arrayOf(PropTypes.string).isRequired,
    init: PropTypes.func.isRequired
};

export const NewBroadcastSchedule = connect(mapNewBroadcastScheduledProps, mapNewBroadcastScheduleDispatch)(NewBroadcastScheduleUi);
