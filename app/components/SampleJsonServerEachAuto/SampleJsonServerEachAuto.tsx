import { useState } from 'react';
import PaginationJson from '../PaginationJson';

interface IData {
	data: string[];
}

const SampleJsonServerEachAuto = () => {
	const [paginationCurrentData, setPaginationCurrentData] = useState<IData['data']>([]);
	return (
		<>
			<h1>Sample Json Server Each - AUTO LOAD</h1>
			<h2>Result:</h2>
			<ul className="list-box-local">
				{paginationCurrentData.map((result: any) => {
					return <li key={result}>{result}</li>;
				})}
			</ul>
			<PaginationJson
				data="http://backend/users"
				scrollDomReference=".list-box-local"
				setData={setPaginationCurrentData}
				saveLocalJson={false}
				autoLoad
				pathData="list"
				perPage={30}
			/>
		</>
	);
};

export default SampleJsonServerEachAuto;
